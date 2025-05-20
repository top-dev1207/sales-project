import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { DashboardProvider } from "./context/DashboardContext";
import Ventas from "./pages/Vantas";
import Existentes from "./pages/Existentes";
import Gastos from "./pages/Gastos";
import Pinta from "./pages/Pinta";
import Pagar from "./pages/Pagar";
import Realizar from "./pages/Realizar";
import UserLoginVisualization from "./pages/UserLoginVisualization";
import NotFound from "./pages/NotFound";

const queryClient = new QueryClient();

const App = () => {
    return (
        <QueryClientProvider client={queryClient}>
            <TooltipProvider>
                <DashboardProvider>
                    <Toaster />
                    <Sonner />
                    <BrowserRouter>
                        <Routes>
                            <Route path="/resumenVer" element={<Navigate to="/resumenVer/realizar"/>} />
                            <Route path="/resumenVer/realizar" element={<Realizar />} />
                            <Route path="/resumenVer/existentes" element={<Existentes />} />
                            <Route path="/resumenVer/ventas" element={<Ventas />} />
                            <Route path="/resumenVer/gastos" element={<Gastos />} />
                            <Route path="/resumenVer/pinta" element={<Pinta />} />
                            <Route path="/resumenVer/pagar" element={<Pagar />} />
                            <Route path="/resumenVer/user" element={<UserLoginVisualization />} />
                            {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
                            <Route path="*" element={<NotFound />} />
                        </Routes>
                    </BrowserRouter>
                </DashboardProvider>
            </TooltipProvider>
        </QueryClientProvider>
    )
};

export default App;
